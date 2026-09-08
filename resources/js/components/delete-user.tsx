import { Form } from '@inertiajs/react';
import { Button, Label, Modal, ModalBody, ModalFooter, ModalHeader } from 'flowbite-react';
import { TriangleAlert } from 'lucide-react';
import { useRef, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { FormCard } from '@/components/form-card';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';

export default function DeleteUser() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const [open, setOpen] = useState(false);

    return (
        <FormCard
            title="منطقة الخطر"
            description="حذف حسابك وجميع موارده"
            icon={TriangleAlert}
        >
            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">تحذير</p>
                    <p className="text-sm">
                        يُرجى المتابعة بحذر، لا يمكن التراجع عن هذا الإجراء.
                    </p>
                </div>

                <Button
                    color="red"
                    data-test="delete-user-button"
                    onClick={() => setOpen(true)}
                >
                    حذف الحساب
                </Button>

                <Modal show={open} onClose={() => setOpen(false)} dismissible>
                    <ModalHeader>
                        هل أنت متأكد أنك تريد حذف حسابك؟
                    </ModalHeader>
                    <ModalBody>
                        <p className="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            بمجرد حذف حسابك، سيتم حذف جميع موارده وبياناته بشكل
                            دائم. يُرجى إدخال كلمة المرور لتأكيد رغبتك في حذف
                            حسابك نهائياً.
                        </p>

                        <Form
                            {...ProfileController.destroy.form()}
                            options={{
                                preserveScroll: true,
                            }}
                            onError={() => passwordInput.current?.focus()}
                            resetOnSuccess
                            className="space-y-6"
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor="password"
                                            className="sr-only"
                                        >
                                            كلمة المرور
                                        </Label>

                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            ref={passwordInput}
                                            placeholder="كلمة المرور"
                                            autoComplete="current-password"
                                        />

                                        <InputError message={errors.password} />
                                    </div>

                                    <ModalFooter className="justify-end gap-2 px-0">
                                        <Button
                                            color="light"
                                            type="button"
                                            onClick={() => {
                                                resetAndClearErrors();
                                                setOpen(false);
                                            }}
                                        >
                                            إلغاء
                                        </Button>

                                        <Button
                                            color="red"
                                            type="submit"
                                            disabled={processing}
                                            data-test="confirm-delete-user-button"
                                        >
                                            حذف الحساب
                                        </Button>
                                    </ModalFooter>
                                </>
                            )}
                        </Form>
                    </ModalBody>
                </Modal>
            </div>
        </FormCard>
    );
}
